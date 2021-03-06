<template>
    <!-- v-app must wrap all the components -->
    <v-app :dark="$root.dark">
        <notifications />
        <v-container fluid grid-list-xs>
            <h1>Materiály</h1>
            <create-model
                v-model="search_string"
                class-name="External"
                label="URL adresa materiálu"
                success-msg="Materiál úspěšně vytvořen"
                @saved="$apollo.queries.externals.refetch()"
                :force-edit="true"
                :enable-file-upload="true"
            ></create-model>
            <v-layout row wrap>
                <v-radio-group v-model="filter_mode" row>
                    <v-radio
                        label="Všechny materiály"
                        value="no-filter"
                    ></v-radio>
                    <v-radio
                        label="Materiály bez autora / přiřazené písničky"
                        value="filter-todo"
                    ></v-radio>
                </v-radio-group>
            </v-layout>
            <v-layout row>
                <v-flex xs12>
                    <v-card>
                        <v-data-table
                            :headers="headers"
                            :items="externals"
                            :search="search_string"
                            :custom-filter="customFilter"
                            :rows-per-page-items="[
                                50,
                                {
                                    text:
                                        '$vuetify.dataIterator.rowsPerPageAll',
                                    value: -1
                                }
                            ]"
                            :loading="$apollo.loading"
                            :no-data-text="
                                $apollo.loading
                                    ? 'Načítám…'
                                    : '$vuetify.noDataText'
                            "
                            :pagination.sync="dtPagination"
                        >
                            <template v-slot:items="props">
                                <td>
                                    <a
                                        :href="
                                            '/admin/external/' +
                                                props.item.id +
                                                '/edit'
                                        "
                                        >{{ getShortUrl(props.item.url) }}</a
                                    >
                                </td>
                                <td>{{ props.item.content_type_string }}</td>
                                <td>
                                    {{
                                        props.item.song_lyric
                                            ? props.item.song_lyric.name
                                            : '–'
                                    }}
                                </td>
                                <td>
                                    {{
                                        props.item.authors
                                            .map(a => a.name)
                                            .join(', ') || '–'
                                    }}
                                </td>
                                <td class="text-nowrap">
                                    <a
                                        class="text-secondary mr-3"
                                        :href="
                                            '/admin/external/' +
                                                props.item.id +
                                                '/edit'
                                        "
                                        ><i class="fas fa-pen"></i></a
                                    ><a
                                        class="text-secondary"
                                        v-on:click="askForm(props.item.id)"
                                        ><i class="fas fa-trash"></i
                                    ></a>
                                </td>
                            </template>
                        </v-data-table>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
    </v-app>
</template>

<style scope>
input {
    border: none;
}
</style>

<script>
import gql from 'graphql-tag';

import removeDiacritics from 'Admin/helpers/removeDiacritics';
import CreateModel from 'Admin/components/CreateModel.vue';

const fetch_items = gql`
    query FetchExternals($is_todo: Boolean) {
        externals(is_todo: $is_todo) {
            id
            url
            content_type_string
            song_lyric {
                name
            }
            authors {
                name
            }
        }
    }
`;

const delete_item = gql`
    mutation DeleteExternal($id: ID!) {
        delete_external(id: $id) {
            id
        }
    }
`;

export default {
    components: {
        CreateModel
    },

    data() {
        return {
            headers: [
                { text: 'Adresa', value: 'url' },
                { text: 'Typ obsahu', value: 'content_type_string' },
                { text: 'Píseň', value: 'song_lyric' },
                { text: 'Autoři', value: 'authors' },
                { text: 'Akce', value: 'actions', sortable: false }
            ],
            search_string: '',
            filter_mode: 'no-filter',
            dtPagination: {}
        };
    },

    watch: {
        filter_mode(val) {
            window.location.hash = val != 'no-filter' ? val : '';
            this.dtPagination.page = 1;
        }
    },

    apollo: {
        externals: {
            query: fetch_items,
            variables() {
                return {
                    is_todo:
                        this.filter_mode == 'filter-todo' ? true : undefined
                };
            },
            result(result) {
                this.buildSearchIndex();
            }
        }
    },

    mounted() {
        if (window.location.hash.length > 2 && this.filter_mode) {
            this.filter_mode = window.location.hash.replace('#', '');
        }

        if (document.getElementById('search')) {
            document.getElementById('search').focus();
        }
    },

    methods: {
        askForm(id) {
            if (confirm('Opravdu chcete smazat daný záznam?')) {
                this.deleteExternal(id);
            }
        },

        deleteExternal(id) {
            this.$apollo
                .mutate({
                    mutation: delete_item,
                    variables: { id: id },
                    refetchQueries: [
                        {
                            query: fetch_items
                        }
                    ]
                })
                .then(result => {
                    console.log('uspesne vymazano');
                })
                .catch(error => {
                    console.log('error');
                });
        },

        getShortUrl(url) {
            if (!url) return;

            const bare = url
                .replace('http://', '')
                .replace('https://', '')
                .replace('www.', '');
            if (bare.length < 50) return bare;

            const head = bare.substring(0, 15);
            const tail = bare.substring(bare.length - 15, bare.length);

            return head + '...' + tail;
        },

        buildSearchIndex() {
            for (var item of this.externals) {
                const authors = item.authors.map(a => a.name).join(' ');
                const str = removeDiacritics(
                    [
                        item.url,
                        authors,
                        item.song_lyric ? item.song_lyric.name : '',
                        item.content_type_string
                    ].join(' ')
                ).toLowerCase();

                this.$set(item, 'search_index', str);
            }
        },

        customFilter(items, search) {
            const needle = removeDiacritics(search).toLowerCase();

            return items.filter(
                item => item.search_index.indexOf(needle) !== -1
            );
        }
    }
};
</script>
